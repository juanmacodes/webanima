#include "RoadSegment.h"
#include "RoadNode.h"
#include "Components/SplineComponent.h"

ARoadSegment::ARoadSegment()
{
    SplineComponent = CreateDefaultSubobject<USplineComponent>(TEXT("Spline"));
    RootComponent = SplineComponent;
    StartNode = nullptr;
    EndNode = nullptr;
}

void ARoadSegment::InitializeSegment(ARoadNode* InStart, ARoadNode* InEnd)
{
    if (StartNode)
    {
        StartNode->RemoveConnectedSegment(this);
    }
    if (EndNode)
    {
        EndNode->RemoveConnectedSegment(this);
    }
    StartNode = InStart;
    EndNode = InEnd;
    if (StartNode)
    {
        StartNode->AddConnectedSegment(this);
    }
    if (EndNode)
    {
        EndNode->AddConnectedSegment(this);
    }
    RefreshSpline();
}

ARoadNode* ARoadSegment::GetStartNode() const
{
    return StartNode;
}

ARoadNode* ARoadSegment::GetEndNode() const
{
    return EndNode;
}

USplineComponent* ARoadSegment::GetSplineComponent() const
{
    return SplineComponent;
}

void ARoadSegment::EndPlay(const EEndPlayReason::Type EndPlayReason)
{
    if (StartNode)
    {
        StartNode->RemoveConnectedSegment(this);
    }
    if (EndNode)
    {
        EndNode->RemoveConnectedSegment(this);
    }
    StartNode = nullptr;
    EndNode = nullptr;
    Super::EndPlay(EndPlayReason);
}

void ARoadSegment::RefreshSpline()
{
    if (!SplineComponent)
    {
        return;
    }
    FVector StartLocation = StartNode ? StartNode->GetActorLocation() : GetActorLocation();
    FVector EndLocation = EndNode ? EndNode->GetActorLocation() : GetActorLocation();
    TArray<FVector> Points;
    Points.Add(StartLocation);
    Points.Add(EndLocation);
    SplineComponent->SetSplinePoints(Points, ESplineCoordinateSpace::World, true);
}
