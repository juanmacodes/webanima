#include "RoadNetworkSubsystem.h"
#include "RoadGenerator.h"
#include "RoadIntersection.h"
#include "RoadNode.h"
#include "RoadSegment.h"
#include "Engine/World.h"
#include "Containers/Queue.h"
#include "Containers/Set.h"

ARoadNetworkSubsystem::ARoadNetworkSubsystem()
{
    PrimaryActorTick.bCanEverTick = false;
    GeneratorClass = ARoadGenerator::StaticClass();
    ActiveGenerator = nullptr;
}

void ARoadNetworkSubsystem::RegisterRoadNode(ARoadNode* Node)
{
    if (!Node)
    {
        return;
    }
    CleanupInvalidEntries();
    RoadNodes.AddUnique(Node);
    UpdateTopology();
}

void ARoadNetworkSubsystem::RegisterRoadSegment(ARoadSegment* Segment)
{
    if (!Segment)
    {
        return;
    }
    CleanupInvalidEntries();
    RoadSegments.AddUnique(Segment);
    RegisterSegmentConnections(Segment);
}

void ARoadNetworkSubsystem::RegisterIntersection(ARoadIntersection* Intersection)
{
    if (!Intersection)
    {
        return;
    }
    CleanupInvalidEntries();
    RoadIntersections.AddUnique(Intersection);
}

void ARoadNetworkSubsystem::GenerateRoadNetwork(int32 Seed)
{
    UWorld* World = GetWorld();
    if (!World)
    {
        return;
    }
    if (!GeneratorClass)
    {
        GeneratorClass = ARoadGenerator::StaticClass();
    }
    if (!ActiveGenerator || ActiveGenerator->IsPendingKill())
    {
        FActorSpawnParameters Params;
        Params.Owner = this;
        Params.SpawnCollisionHandlingOverride = ESpawnActorCollisionHandlingMethod::AlwaysSpawn;
        ActiveGenerator = World->SpawnActor<ARoadGenerator>(GeneratorClass, GetActorLocation(), GetActorRotation(), Params);
        if (!ActiveGenerator)
        {
            return;
        }
    }
    ActiveGenerator->SetNetworkActor(this);
    ActiveGenerator->ClearGeneratedNetwork();
    CleanupInvalidEntries();
    ActiveGenerator->SetSeed(Seed);
    ActiveGenerator->GenerateNetwork();
    CleanupInvalidEntries();
}

void ARoadNetworkSubsystem::ClearRoadNetwork()
{
    if (ActiveGenerator)
    {
        ActiveGenerator->ClearGeneratedNetwork();
    }
    for (TWeakObjectPtr<ARoadSegment>& SegmentPtr : RoadSegments)
    {
        if (SegmentPtr.IsValid())
        {
            SegmentPtr->Destroy();
        }
    }
    for (TWeakObjectPtr<ARoadNode>& NodePtr : RoadNodes)
    {
        if (NodePtr.IsValid())
        {
            NodePtr->Destroy();
        }
    }
    for (TWeakObjectPtr<ARoadIntersection>& IntersectionPtr : RoadIntersections)
    {
        if (IntersectionPtr.IsValid())
        {
            IntersectionPtr->Destroy();
        }
    }
    RoadNodes.Reset();
    RoadSegments.Reset();
    RoadIntersections.Reset();
    NodeSegments.Reset();
}

TArray<ARoadSegment*> ARoadNetworkSubsystem::FindRouteBetween(ARoadNode* A, ARoadNode* B)
{
    TArray<ARoadSegment*> Route;
    if (!A || !B || A == B)
    {
        return Route;
    }
    CleanupInvalidEntries();
    TQueue<ARoadNode*> Frontier;
    TSet<ARoadNode*> Visited;
    TMap<ARoadNode*, ARoadNode*> PreviousNode;
    TMap<ARoadNode*, ARoadSegment*> PreviousSegment;
    Frontier.Enqueue(A);
    Visited.Add(A);
    while (!Frontier.IsEmpty())
    {
        ARoadNode* Current = nullptr;
        Frontier.Dequeue(Current);
        if (!Current)
        {
            continue;
        }
        if (Current == B)
        {
            break;
        }
        const TArray<TWeakObjectPtr<ARoadSegment>>* SegmentList = NodeSegments.Find(Current);
        if (!SegmentList)
        {
            continue;
        }
        for (const TWeakObjectPtr<ARoadSegment>& SegmentPtr : *SegmentList)
        {
            if (!SegmentPtr.IsValid())
            {
                continue;
            }
            ARoadSegment* Segment = SegmentPtr.Get();
            ARoadNode* NextNode = Segment->GetStartNode();
            if (NextNode == Current)
            {
                NextNode = Segment->GetEndNode();
            }
            else if (Segment->GetEndNode() == Current)
            {
                NextNode = Segment->GetStartNode();
            }
            else
            {
                continue;
            }
            if (!NextNode || Visited.Contains(NextNode))
            {
                continue;
            }
            Visited.Add(NextNode);
            PreviousNode.Add(NextNode, Current);
            PreviousSegment.Add(NextNode, Segment);
            Frontier.Enqueue(NextNode);
        }
    }
    if (!Visited.Contains(B))
    {
        return Route;
    }
    ARoadNode* CurrentNode = B;
    while (CurrentNode && CurrentNode != A)
    {
        ARoadSegment** SegmentPtr = PreviousSegment.Find(CurrentNode);
        if (!SegmentPtr)
        {
            break;
        }
        Route.Insert(*SegmentPtr, 0);
        ARoadNode** NodePtr = PreviousNode.Find(CurrentNode);
        if (!NodePtr)
        {
            break;
        }
        CurrentNode = *NodePtr;
    }
    return Route;
}

void ARoadNetworkSubsystem::BeginDestroy()
{
    ClearRoadNetwork();
    if (ActiveGenerator)
    {
        ActiveGenerator->Destroy();
        ActiveGenerator = nullptr;
    }
    Super::BeginDestroy();
}

void ARoadNetworkSubsystem::CleanupInvalidEntries()
{
    RoadNodes.RemoveAll([](const TWeakObjectPtr<ARoadNode>& NodePtr)
    {
        return !NodePtr.IsValid();
    });
    RoadSegments.RemoveAll([](const TWeakObjectPtr<ARoadSegment>& SegmentPtr)
    {
        return !SegmentPtr.IsValid();
    });
    RoadIntersections.RemoveAll([](const TWeakObjectPtr<ARoadIntersection>& IntersectionPtr)
    {
        return !IntersectionPtr.IsValid();
    });
    UpdateTopology();
}

void ARoadNetworkSubsystem::UpdateTopology()
{
    NodeSegments.Reset();
    for (const TWeakObjectPtr<ARoadSegment>& SegmentPtr : RoadSegments)
    {
        if (!SegmentPtr.IsValid())
        {
            continue;
        }
        RegisterSegmentConnections(SegmentPtr.Get());
    }
}

void ARoadNetworkSubsystem::RegisterSegmentConnections(ARoadSegment* Segment)
{
    if (!Segment)
    {
        return;
    }
    ARoadNode* StartNode = Segment->GetStartNode();
    ARoadNode* EndNode = Segment->GetEndNode();
    if (StartNode)
    {
        TArray<TWeakObjectPtr<ARoadSegment>>& Segments = NodeSegments.FindOrAdd(StartNode);
        Segments.AddUnique(Segment);
    }
    if (EndNode)
    {
        TArray<TWeakObjectPtr<ARoadSegment>>& Segments = NodeSegments.FindOrAdd(EndNode);
        Segments.AddUnique(Segment);
    }
}
