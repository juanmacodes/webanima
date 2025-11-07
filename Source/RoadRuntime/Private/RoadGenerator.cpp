#include "RoadGenerator.h"
#include "RoadNetworkSubsystem.h"
#include "RoadNode.h"
#include "RoadSegment.h"
#include "Engine/World.h"
#include "EngineUtils.h"

ARoadGenerator::ARoadGenerator()
{
    PrimaryActorTick.bCanEverTick = false;
    Seed = 1337;
    MinIntersectionSpacing = 500.f;
    MaxRoads = 8;
    GenerationRadius = 5000.f;
    RoadNodeClass = ARoadNode::StaticClass();
    RoadSegmentClass = ARoadSegment::StaticClass();
}

void ARoadGenerator::BeginPlay()
{
    Super::BeginPlay();
}

void ARoadGenerator::GenerateNetwork()
{
    UWorld* World = GetWorld();
    if (!World || !RoadNodeClass || !RoadSegmentClass)
    {
        return;
    }
    DestroyGeneratedActors();
    URoadNetworkSubsystem* Subsystem = World->GetSubsystem<URoadNetworkSubsystem>();
    FRandomStream RandomStream(Seed);
    TArray<ARoadNode*> Nodes;
    const int32 NodeTarget = FMath::Max(MaxRoads + 1, 2);
    const float MinSpacing = FMath::Max(MinIntersectionSpacing, 0.f);
    for (int32 Index = 0; Index < NodeTarget; ++Index)
    {
        bool bSpawned = false;
        for (int32 Attempt = 0; Attempt < 64 && !bSpawned; ++Attempt)
        {
            const float Angle = RandomStream.FRandRange(0.f, UE_TWO_PI);
            const float Radius = RandomStream.FRandRange(0.f, GenerationRadius);
            FVector Candidate = GetActorLocation();
            Candidate.X += FMath::Cos(Angle) * Radius;
            Candidate.Y += FMath::Sin(Angle) * Radius;
            if (!IsValidNodeLocation(Candidate, Nodes))
            {
                continue;
            }
            FActorSpawnParameters Params;
            Params.Owner = this;
            Params.SpawnCollisionHandlingOverride = ESpawnActorCollisionHandlingMethod::AlwaysSpawn;
            ARoadNode* Node = World->SpawnActor<ARoadNode>(RoadNodeClass, Candidate, GetActorRotation(), Params);
            if (!Node)
            {
                continue;
            }
            Nodes.Add(Node);
            GeneratedNodes.Add(Node);
            if (Subsystem)
            {
                Subsystem->RegisterNode(Node);
            }
            bSpawned = true;
        }
    }
    if (Nodes.Num() < 2 || MaxRoads <= 0)
    {
        return;
    }
    int32 RoadsCreated = 0;
    const float MinDistanceSq = MinSpacing * MinSpacing;
    for (int32 IndexA = 0; IndexA < Nodes.Num() && RoadsCreated < MaxRoads; ++IndexA)
    {
        ARoadNode* NodeA = Nodes[IndexA];
        if (!NodeA)
        {
            continue;
        }
        for (int32 IndexB = IndexA + 1; IndexB < Nodes.Num() && RoadsCreated < MaxRoads; ++IndexB)
        {
            ARoadNode* NodeB = Nodes[IndexB];
            if (!NodeB)
            {
                continue;
            }
            const float DistanceSq = FVector::DistSquared(NodeA->GetActorLocation(), NodeB->GetActorLocation());
            if (DistanceSq < MinDistanceSq)
            {
                continue;
            }
            bool bAlreadyConnected = false;
            const TArray<TWeakObjectPtr<ARoadSegment>>& NodeSegments = NodeA->GetConnectedSegments();
            for (const TWeakObjectPtr<ARoadSegment>& SegmentPtr : NodeSegments)
            {
                if (!SegmentPtr.IsValid())
                {
                    continue;
                }
                ARoadSegment* Segment = SegmentPtr.Get();
                if ((Segment->GetStartNode() == NodeA && Segment->GetEndNode() == NodeB) || (Segment->GetStartNode() == NodeB && Segment->GetEndNode() == NodeA))
                {
                    bAlreadyConnected = true;
                    break;
                }
            }
            if (bAlreadyConnected)
            {
                continue;
            }
            ARoadSegment* Segment = SpawnRoadBetween(NodeA, NodeB);
            if (!Segment)
            {
                continue;
            }
            ++RoadsCreated;
        }
    }
}

void ARoadGenerator::ConnectPlacedNodes()
{
    UWorld* World = GetWorld();
    if (!World)
    {
        return;
    }
    URoadNetworkSubsystem* Subsystem = World->GetSubsystem<URoadNetworkSubsystem>();
    TArray<ARoadNode*> Nodes;
    for (TActorIterator<ARoadNode> It(World); It; ++It)
    {
        ARoadNode* Node = *It;
        if (!Node)
        {
            continue;
        }
        if (Node->GetOwner() == this)
        {
            continue;
        }
        Nodes.Add(Node);
    }
    if (Nodes.Num() < 2)
    {
        return;
    }
    Nodes.Sort([](ARoadNode* const& A, ARoadNode* const& B)
    {
        if (A == B)
        {
            return false;
        }
        if (!A)
        {
            return false;
        }
        if (!B)
        {
            return true;
        }
        const FVector LocationA = A->GetActorLocation();
        const FVector LocationB = B->GetActorLocation();
        if (FMath::IsNearlyEqual(LocationA.X, LocationB.X))
        {
            if (FMath::IsNearlyEqual(LocationA.Y, LocationB.Y))
            {
                return LocationA.Z < LocationB.Z;
            }
            return LocationA.Y < LocationB.Y;
        }
        return LocationA.X < LocationB.X;
    });
    const float MinDistanceSq = FMath::Max(MinIntersectionSpacing, 0.f) * FMath::Max(MinIntersectionSpacing, 0.f);
    if (Subsystem)
    {
        for (ARoadNode* Node : Nodes)
        {
            if (Node)
            {
                Subsystem->RegisterNode(Node);
            }
        }
    }
    for (int32 Index = 0; Index < Nodes.Num() - 1; ++Index)
    {
        ARoadNode* NodeA = Nodes[Index];
        ARoadNode* NodeB = Nodes[Index + 1];
        if (!NodeA || !NodeB)
        {
            continue;
        }
        const float DistanceSq = FVector::DistSquared(NodeA->GetActorLocation(), NodeB->GetActorLocation());
        if (DistanceSq < MinDistanceSq)
        {
            continue;
        }
        bool bAlreadyConnected = false;
        const TArray<TWeakObjectPtr<ARoadSegment>>& NodeSegments = NodeA->GetConnectedSegments();
        for (const TWeakObjectPtr<ARoadSegment>& SegmentPtr : NodeSegments)
        {
            if (!SegmentPtr.IsValid())
            {
                continue;
            }
            ARoadSegment* Segment = SegmentPtr.Get();
            if ((Segment->GetStartNode() == NodeA && Segment->GetEndNode() == NodeB) || (Segment->GetStartNode() == NodeB && Segment->GetEndNode() == NodeA))
            {
                bAlreadyConnected = true;
                break;
            }
        }
        if (bAlreadyConnected)
        {
            continue;
        }
        ARoadSegment* Segment = SpawnRoadBetween(NodeA, NodeB);
    }
}

ARoadSegment* ARoadGenerator::SpawnRoadBetween(ARoadNode* A, ARoadNode* B)
{
    if (!A || !B || A == B || !RoadSegmentClass)
    {
        return nullptr;
    }
    UWorld* World = GetWorld();
    if (!World)
    {
        return nullptr;
    }
    const FVector StartLocation = A->GetActorLocation();
    const FVector EndLocation = B->GetActorLocation();
    const FVector Midpoint = (StartLocation + EndLocation) * 0.5f;
    const FRotator Rotation = (EndLocation - StartLocation).Rotation();
    FActorSpawnParameters Params;
    Params.Owner = this;
    Params.SpawnCollisionHandlingOverride = ESpawnActorCollisionHandlingMethod::AlwaysSpawn;
    ARoadSegment* Segment = World->SpawnActor<ARoadSegment>(RoadSegmentClass, Midpoint, Rotation, Params);
    if (!Segment)
    {
        return nullptr;
    }
    Segment->InitializeSegment(A, B);
    GeneratedSegments.AddUnique(Segment);
    if (URoadNetworkSubsystem* Subsystem = World->GetSubsystem<URoadNetworkSubsystem>())
    {
        Subsystem->RegisterSegment(Segment);
    }
    return Segment;
}

bool ARoadGenerator::IsValidNodeLocation(const FVector& Location, const TArray<ARoadNode*>& Nodes) const
{
    const float MinSpacing = FMath::Max(MinIntersectionSpacing, 0.f);
    const float MinDistanceSq = MinSpacing * MinSpacing;
    for (ARoadNode* Node : Nodes)
    {
        if (!Node)
        {
            continue;
        }
        if (FVector::DistSquared(Location, Node->GetActorLocation()) < MinDistanceSq)
        {
            return false;
        }
    }
    return true;
}

void ARoadGenerator::DestroyGeneratedActors()
{
    UWorld* World = GetWorld();
    URoadNetworkSubsystem* Subsystem = World ? World->GetSubsystem<URoadNetworkSubsystem>() : nullptr;
    for (TWeakObjectPtr<ARoadSegment>& SegmentPtr : GeneratedSegments)
    {
        if (!SegmentPtr.IsValid())
        {
            continue;
        }
        if (Subsystem)
        {
            Subsystem->UnregisterSegment(SegmentPtr.Get());
        }
        SegmentPtr->Destroy();
    }
    GeneratedSegments.Reset();
    for (TWeakObjectPtr<ARoadNode>& NodePtr : GeneratedNodes)
    {
        if (!NodePtr.IsValid())
        {
            continue;
        }
        if (Subsystem)
        {
            Subsystem->UnregisterNode(NodePtr.Get());
        }
        if (NodePtr->GetOwner() == this)
        {
            NodePtr->Destroy();
        }
    }
    GeneratedNodes.Reset();
}
